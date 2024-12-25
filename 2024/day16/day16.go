package main

import (
	"flag"
	"fmt"
	"log"
	"math"
	"os"
	"runtime/pprof"
	"strings"
)

type Tile string

const (
	wall  Tile = "#"
	empty Tile = "."
	start Tile = "S"
	end   Tile = "E"
)

type Coordinates struct {
	x, y int
}
type MoveType int

const (
	coordinates          MoveType = iota
	clockwiseTurn        MoveType = 1
	counterclockwiseTurn MoveType = 2
)

type Direction int

const (
	up    Direction = 0
	down  Direction = 1
	left  Direction = 2
	right Direction = 3
)

type ReindeerPosition struct {
	coordinates Coordinates
	direction   Direction
}
type AdjacentNode struct {
	reindeerPosition ReindeerPosition
	cost             int
}

var raceTrack [][]Tile
var cpuprofile = flag.String("cpuprofile", "", "write cpu profile to `file`")

func main() {
	// Parse command-line flags
	flag.Parse()

	// Start CPU profiling if the -cpuprofile flag is set
	if *cpuprofile != "" {
		f, err := os.Create(*cpuprofile)
		if err != nil {
			log.Fatal("could not create CPU profile: ", err)
		}
		defer f.Close()

		if err := pprof.StartCPUProfile(f); err != nil {
			log.Fatal("could not start CPU profile: ", err)
		}
		defer pprof.StopCPUProfile()
	}
	filenames := []string{"testInput", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		lines := strings.Split(string(fileContents), "\n")
		raceTrack = make([][]Tile, len(lines))
		var startPosition ReindeerPosition
		var endCoordinates Coordinates
		for y := 0; y < len(lines); y++ {
			raceTrack[y] = make([]Tile, len(lines))
			for x := 0; x < len(lines[y]); x++ {
				raceTrack[y][x] = Tile(lines[y][x])
				if raceTrack[y][x] == start {
					startPosition = ReindeerPosition{Coordinates{x, y}, right}
				} else if raceTrack[y][x] == end {
					endCoordinates = Coordinates{x, y}
				}
			}
		}
		var nodeCosts = make([][][4]int, len(raceTrack))
		for y := 0; y < len(raceTrack); y++ {
			nodeCosts[y] = make([][4]int, len(raceTrack[y]))
			for x := 0; x < len(raceTrack[y]); x++ {
				nodeCosts[y][x] = [4]int{
					math.MaxInt,
					math.MaxInt,
					math.MaxInt,
					math.MaxInt,
				}
			}
		}
		visitedNodes := make(map[int]bool)
		nodeCosts[startPosition.coordinates.y][startPosition.coordinates.x][startPosition.direction] = 0
		currentPosition := startPosition
		i := 0
	dijkstraLoop:
		for {
			currentNodeCost := nodeCosts[currentPosition.coordinates.y][currentPosition.coordinates.x][currentPosition.direction]
			adjacentPositions := findAdjacentPositions(currentPosition)
			for _, adjacentPosition := range adjacentPositions {
				nodeCosts[adjacentPosition.reindeerPosition.coordinates.y][adjacentPosition.reindeerPosition.coordinates.x][adjacentPosition.reindeerPosition.direction] =
					min(nodeCosts[adjacentPosition.reindeerPosition.coordinates.y][adjacentPosition.reindeerPosition.coordinates.x][adjacentPosition.reindeerPosition.direction],
						adjacentPosition.cost+currentNodeCost)

			}
			i++

			visitedNodes[reindeerPositionKey(currentPosition.coordinates.x, currentPosition.coordinates.y, int(currentPosition.direction))] = true
			currentPosition = findNextNode(visitedNodes, nodeCosts)
			if currentPosition.tile() == end {
				break dijkstraLoop
			}
		}

		minCost := math.MaxInt
		for direction := range 4 {
			minCost = min(minCost, nodeCosts[endCoordinates.y][endCoordinates.x][direction])
		}

		println(minCost)
	}
}

func findNextNode(visitedNodes map[int]bool, nodeCosts [][][4]int) ReindeerPosition {
	var currentPosition ReindeerPosition
	minCost := math.MaxInt
	for y := 0; y < len(raceTrack); y++ {
		for x := 0; x < len(raceTrack[y]); x++ {
			for direction := range 4 {
				if nodeCosts[y][x][direction] >= minCost {
					continue
				}
				_, ok := visitedNodes[reindeerPositionKey(x, y, direction)]
				if ok {
					continue
				}
				minCost = nodeCosts[y][x][direction]
				currentPosition = ReindeerPosition{Coordinates{x, y}, Direction(direction)}
			}
		}
	}
	return currentPosition
}
func findTileAtCoordinates(coordinates Coordinates) Tile {
	return raceTrack[coordinates.y][coordinates.x]
}
func (reindeerPosition *ReindeerPosition) tile() Tile {
	return findTileAtCoordinates(reindeerPosition.coordinates)
}
func reindeerPositionKey(x int, y int, direction int) int {
	return x*100000 + y*10 + direction
}
func findAdjacentPositions(position ReindeerPosition) []AdjacentNode {
	positions := make([]AdjacentNode, 0)
	moveForwardPosition := position
	switch position.direction {
	case up:
		moveForwardPosition.coordinates.y--
		break
	case down:
		moveForwardPosition.coordinates.y++
		break
	case left:
		moveForwardPosition.coordinates.x--
		break
	case right:
		moveForwardPosition.coordinates.x++
		break
	}
	if moveForwardPosition.tile() != wall {
		positions = append(positions, AdjacentNode{moveForwardPosition, 1})
	}
	turnLeftPosition := AdjacentNode{position, 1000}
	turnRightPosition := AdjacentNode{position, 1000}
	switch position.direction {
	case up:
		turnLeftPosition.reindeerPosition.direction = left
		turnRightPosition.reindeerPosition.direction = right
		break
	case down:
		turnLeftPosition.reindeerPosition.direction = right
		turnRightPosition.reindeerPosition.direction = left
		break
	case left:
		turnLeftPosition.reindeerPosition.direction = up
		turnRightPosition.reindeerPosition.direction = down
		break
	case right:
		turnLeftPosition.reindeerPosition.direction = up
		turnRightPosition.reindeerPosition.direction = down
		break
	default:
		panic("Unexpected direction")
	}
	positions = append(positions, turnLeftPosition, turnRightPosition)

	return positions
}
