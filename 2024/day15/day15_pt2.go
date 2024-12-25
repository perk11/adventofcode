package main

import (
	"fmt"
	"os"
	"slices"
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
	box_left  Tile = "["
	box_right Tile = "]"
	empty     Tile = "."
	wall      Tile = "#"
	robot     Tile = "@"
)

type Coordinates struct {
	x, y int
}

var maxX, maxY int

func main() {
	filenames := []string{"testInput3", "testInput", "input"}
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
			warehouse[lineIndex] = make([]Tile, len(lineChars)*2)
			for x, char := range lineChars {
				if char == string(robot) {
					robotPosition = Coordinates{x * 2, lineIndex}
					warehouse[lineIndex][x*2] = Tile(char)
					warehouse[lineIndex][x*2+1] = empty
				} else if char == string(wall) || char == string(empty) {
					warehouse[lineIndex][x*2] = Tile(char)
					warehouse[lineIndex][x*2+1] = Tile(char)
				} else if char == "O" {
					warehouse[lineIndex][x*2] = box_left
					warehouse[lineIndex][x*2+1] = box_right
				} else {
					panic("Unknown tile: " + char)
				}
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
			//print(move)
			robotPosition = performMove(robotPosition, move, &warehouse)
		}

		total := 0
		for y := 0; y < len(warehouse); y++ {
			for x := 0; x < len(warehouse[y]); x++ {
				if warehouse[y][x] == box_left {
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
func isPassable(coordinates Coordinates, direction Direction, wareHouse *[][]Tile, checkedCoordinates []Coordinates) bool {
	coordinatesAfterMove := getCoordinateAfterMove(coordinates, direction)
	isBox := false
	var otherCoordinate Coordinates
	switch (*wareHouse)[coordinatesAfterMove.y][coordinatesAfterMove.x] {
	case empty:
		return true
	//case robot:
	//	return true
	case wall:
		return false
	case box_left:
		otherCoordinate = coordinatesAfterMove
		otherCoordinate.x++
		isBox = true
		break
	case box_right:
		otherCoordinate = coordinatesAfterMove
		otherCoordinate.x--
		isBox = true
		break
	}
	if isBox {

		checkedCoordinates = append(checkedCoordinates, coordinatesAfterMove, otherCoordinate)
		if !isPassable(coordinatesAfterMove, direction, wareHouse, checkedCoordinates) {
			return false
		}
		if !slices.Contains(checkedCoordinates, otherCoordinate) {
			if !isPassable(otherCoordinate, direction, wareHouse, checkedCoordinates) {
				return false
			}
		}
		return true
	}
	panic("Unexpected tile: " + (*wareHouse)[coordinatesAfterMove.y][coordinatesAfterMove.x])
}
func attemptMove(coordinates Coordinates, direction Direction, wareHouse *[][]Tile, movedCoordinates []Coordinates) bool {
	currentTile := (*wareHouse)[coordinates.y][coordinates.x]
	wareHouseBackup := make([][]Tile, len(*wareHouse), len((*wareHouse)[0]))
	for i := range *wareHouse {
		wareHouseBackup[i] = make([]Tile, len((*wareHouse)[i]))
		copy(wareHouseBackup[i], (*wareHouse)[i])
	}
	if currentTile == empty {
		return true
	}

	coordinatesAfterMove := getCoordinateAfterMove(coordinates, direction)
	tileAfterMove := (*wareHouse)[coordinatesAfterMove.y][coordinatesAfterMove.x]
	if tileAfterMove == wall {
		return false
	}
	if tileAfterMove == box_left || tileAfterMove == box_right {
		moveMe := false
		if !slices.Contains(movedCoordinates, coordinatesAfterMove) {
			moveMe = true
			movedCoordinates = append(movedCoordinates, coordinatesAfterMove)
		}
		otherBoxPart := coordinatesAfterMove
		if tileAfterMove == box_left {
			otherBoxPart.x++
		} else {
			otherBoxPart.x--
		}
		if !slices.Contains(movedCoordinates, otherBoxPart) {
			movedCoordinates = append(movedCoordinates, coordinates)
			if !attemptMove(otherBoxPart, direction, wareHouse, movedCoordinates) {
				return false
			}
		}
		if moveMe {
			if !attemptMove(coordinatesAfterMove, direction, wareHouse, movedCoordinates) {
				restoreWareHouseFromBackup(wareHouse, wareHouseBackup)
				return false
			}
		}
	}

	if currentTile == wall {
		panic("Tried to move a wall")
	}

	(*wareHouse)[coordinatesAfterMove.y][coordinatesAfterMove.x] = currentTile
	(*wareHouse)[coordinates.y][coordinates.x] = empty
	return true
}
func restoreWareHouseFromBackup(wareHouse *[][]Tile, backup [][]Tile) {
	for y := 0; y < len(backup); y++ {
		for x := 0; x < len(backup[y]); x++ {
			(*wareHouse)[y][x] = backup[y][x]
		}
	}
}
func performMove(robotPosition Coordinates, move Direction, wareHouse *[][]Tile) Coordinates {
	//if !isPassable(robotPosition, move, wareHouse, []Coordinates{}) {
	//	return robotPosition
	//}
	if attemptMove(robotPosition, move, wareHouse, []Coordinates{}) {
		return getCoordinateAfterMove(robotPosition, move)
	}
	return robotPosition

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
