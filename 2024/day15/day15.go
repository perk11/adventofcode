package main

import (
	"fmt"
	"os"
	"strings"
)

type Direction string

const (
	down  Direction = "v"
	up    Direction = "^"
	left  Direction = "<"
	right Direction = ">"
)

type Tile string

const (
	box   Tile = "O"
	empty Tile = "."
	wall  Tile = "#"
	robot Tile = "@"
)

type Coordinates struct {
	x, y int
}

func main() {
	filenames := []string{"testInput2", "testInput", "input"}
	for _, fileName := range filenames {

		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		lines := strings.Split(string(fileContents), "\n")
		var newLineIndex int
		for index, line := range lines {
			if line == "" {
				newLineIndex = index
				break
			}
		}
		warehouse := make([][]Tile, newLineIndex)
		var robotPosition Coordinates
		for lineIndex := 0; lineIndex < newLineIndex; lineIndex++ {
			lineChars := strings.Split(lines[lineIndex], "")
			warehouse[lineIndex] = make([]Tile, len(lineChars))
			for x, char := range lineChars {
				if char == string(robot) {
					robotPosition = Coordinates{x, lineIndex}
				}
				warehouse[lineIndex][x] = Tile(char)
			}
		}
		moves := make([]Direction, 0)
		for lineIndex := newLineIndex + 1; lineIndex < len(lines); lineIndex++ {
			lineChars := strings.Split(lines[lineIndex], "")
			for _, char := range lineChars {
				moves = append(moves, Direction(char))
			}
		}
		for _, move := range moves {
			//printWarehouseMap(&warehouse)
			robotPosition = performMove(robotPosition, move, &warehouse)
		}

		total := 0
		for y := 0; y < len(warehouse); y++ {
			for x := 0; x < len(warehouse); x++ {
				if warehouse[y][x] == box {
					total += y*100 + x
				}
			}
		}
		println(total)
	}
}
func getCoordinateAfterMove(coordinates Coordinates, move Direction) Coordinates {
	switch move {
	case down:
		return Coordinates{coordinates.x, coordinates.y + 1}
	case up:
		return Coordinates{coordinates.x, coordinates.y - 1}
	case left:
		return Coordinates{coordinates.x - 1, coordinates.y}
	case right:
		return Coordinates{coordinates.x + 1, coordinates.y}
	}
	panic("Unexpected move: " + string(move))
}
func isPassable(coordinates Coordinates, direction Direction, wareHouse *[][]Tile) bool {
	coordinatesAfterMove := getCoordinateAfterMove(coordinates, direction)
	switch (*wareHouse)[coordinatesAfterMove.y][coordinatesAfterMove.x] {
	case empty:
		return true
	//case robot:
	//	return true
	case wall:
		return false
	case box:
		return isPassable(coordinatesAfterMove, direction, wareHouse)
	}
	panic("Unexpected tile: " + (*wareHouse)[coordinatesAfterMove.y][coordinatesAfterMove.x])
}
func moveNoCollisionCheck(coordinates Coordinates, direction Direction, wareHouse *[][]Tile) {
	currentTile := (*wareHouse)[coordinates.y][coordinates.x]
	if currentTile == empty {
		return
	}

	coordinatesAfterMove := getCoordinateAfterMove(coordinates, direction)
	tileAfterMove := (*wareHouse)[coordinatesAfterMove.y][coordinatesAfterMove.x]
	if tileAfterMove == box {
		moveNoCollisionCheck(coordinatesAfterMove, direction, wareHouse)
	}
	if currentTile == wall {
		panic("Tried to move a wall")
	}
	if tileAfterMove == wall {
		panic("Tried to move into a wall")
	}
	(*wareHouse)[coordinatesAfterMove.y][coordinatesAfterMove.x] = currentTile
	(*wareHouse)[coordinates.y][coordinates.x] = empty

}
func performMove(robotPosition Coordinates, move Direction, wareHouse *[][]Tile) Coordinates {
	if !isPassable(robotPosition, move, wareHouse) {
		return robotPosition
	}
	moveNoCollisionCheck(robotPosition, move, wareHouse)
	return getCoordinateAfterMove(robotPosition, move)

}
func printWarehouseMap(wareHouse *[][]Tile) {
	print("\n")
	for y := 0; y < len(*wareHouse); y++ {
		for x := 0; x < len((*wareHouse)[y]); x++ {
			print((*wareHouse)[y][x])
		}
		print("\n")

	}
}
